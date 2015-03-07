# Auto-hide pre tags in given elements
autoHide = (selector) ->
  $(selector).click (event) ->
    preTag = $(event.target).find('pre')
    if preTag.is ':visible'
      preTag.hide()
    else
      preTag.show()
  $("#{selector} pre").hide()

# Generating sleep chart
generateChart = (selector, sleepInfo) ->
  scaleFactor = 1.0
  offsetMinutes = 20 * 60
  wrapperDiv = d3.select selector

  wrapperDiv.style 'width', (scaleFactor * 16 * 60) + 'px'

  singleBar = wrapperDiv.selectAll('div')
    .data(sleepInfo)
    .enter()
    .append('div')

  singleBar
    .style 'width', (d) ->
      return (scaleFactor * d.timeInBed) + 'px'
    .style 'margin-left', (d) ->
      return '0px' if d.startTime == ''
      parts = d.startTime.split ':'
      hours = parseInt parts[0], 10
      minutes = parseInt parts[1], 10
      hours += 24 if hours < 12
      return (scaleFactor * (hours * 60 + minutes - offsetMinutes)) + 'px'
    .text (d) ->
      return "\xA0" if d.startTime == ''
      hours = Math.floor d.timeInBed / 60
      minutes = d.timeInBed % 60
      return "#{d.dateTime} - #{hours}h #{minutes}m"

  singleBar
    .append('div')
    .style('float', 'left')
    .text (d) ->
      return '' if d.startTime == ''
      parts = d.startTime.split(':')
      hours = parseInt parts[0], 10
      minutes = parseInt parts[1], 10
      ampm = if hours >= 12 then 'pm' else 'am'
      hours = if (hours % 12 == 0) then 12 else hours % 12
      minutes = if minutes < 10 then '0' + minutes else minutes
      return "#{hours}:#{minutes}#{ampm}"

  singleBar
    .append('div')
    .style('float', 'right')
    .text (d) ->
      return '' if d.startTime == ''
      parts = d.startTime.split ':'
      hours = parseInt parts[0], 10
      minutes = parseInt parts[1], 10
      startTimeMinutes = hours * 60 + minutes
      endTimeMinutes = (startTimeMinutes + parseInt(d.timeInBed, 10)) % (24 * 60)

      hours = Math.floor endTimeMinutes / 60
      ampm = if hours >= 12 then 'pm' else 'am'
      minutes = endTimeMinutes % 60
      minutes = if minutes < 10 then '0' + minutes else minutes
      return "#{hours}:#{minutes}#{ampm}"



# Put methods we want to export into a 'namespace' on window
window.fitbitGraphs =
  autoHide: autoHide
  generateChart: generateChart

