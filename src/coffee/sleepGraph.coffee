$ ->
  # D3 generate sleep start time chart
  scaleFactor = 1.0
  offsetMinutes = 20 * 60
  wrapperDiv = d3.select('.sleep_start_times')
  wrapperDiv.style('width', (scaleFactor * (16 * 60)) + 'px')
  anotherLine = 10

