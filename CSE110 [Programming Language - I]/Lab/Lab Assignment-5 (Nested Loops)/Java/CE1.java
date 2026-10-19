import java.util.Scanner;

public class CE1{
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("The value of N: ");
        int n = sc.nextInt();
        int sum = 0;
        int y = 0;

        for (int i = 1; i <= n; i++)
        {
            sum += i;
            y -= sum;
        }
        System.out.println("The value of y: " + y);
    }
}